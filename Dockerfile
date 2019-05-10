FROM ubuntu:18.04
ARG USER
ARG UID
ARG GID

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y software-properties-common locales

RUN locale-gen en_US.UTF-8
ENV LANG=en_US.UTF-8

RUN apt-get update && \
    apt-get install -y --force-yes \
	    nano \
	    git \
	    curl \
        zsh

RUN apt-add-repository -y ppa:ondrej/php && apt-get update && apt-get install -y --force-yes \
	php7.3-cli \
	php7.3-curl \
	php7.3-intl \
	php7.3-mysql \
	php7.3-xml \
	php7.3-mbstring \
	php7.3-bcmath \
	php7.3-zip \
	php7.3-opcache \
	php7.3-bz2 \
	php7.3-gmp \
    php-xdebug \
    php7.3-redis

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN groupadd -g ${GID} -r ${USER} && useradd -u ${UID} -rm -s /bin/zsh -g ${USER} -G audio,video ${USER} \
    && mkdir -p /home/${USER}/Downloads \
    && chown -R ${USER}:${USER} /home/${USER}

WORKDIR /home/${USER}

USER ${USER}

RUN git clone git://github.com/robbyrussell/oh-my-zsh.git /home/${USER}/.oh-my-zsh \
      && cp /home/${USER}/.oh-my-zsh/templates/zshrc.zsh-template /home/${USER}/.zshrc \
      && sed -i.bak 's/robbyrussell/nebirhos/' /home/${USER}/.zshrc

RUN echo "plugins=(git yarn)" >> /home/${USER}/.zshrc

# from https://hub.docker.com/r/themattrix/develop/~/dockerfile/
RUN git clone https://github.com/junegunn/fzf.git /home/${USER}/.fzf \
    && (cd /home/${USER}/.fzf) \
    && echo "export FZF_DEFAULT_OPTS='--no-height --no-reverse'" >> /home/${USER}/.zshrc

RUN mkdir /home/${USER}/current
RUN touch /home/${USER}/.zsh_history
WORKDIR /home/${USER}/current
